<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-5xl mx-auto">
            
            {{-- HEADER --}}
            <div class="mb-8 border-b border-white/10 pb-6">
                <a href="{{ route('admin.sub_admins.index') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-4 transition-colors text-sm font-bold tracking-wide uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Managers
                </a>
                <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Edit Manager</h1>
                <p class="text-blue-200 mt-1 text-lg font-medium">Update details for <span class="text-white font-bold">{{ $user->name }}</span></p>
            </div>

            <form action="{{ route('admin.sub_admins.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                {{-- SECTION 1: ACCOUNT DETAILS --}}
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 mb-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-user-pen text-blue-400"></i> Account Details
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-blue-300 uppercase mb-2">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition h-12" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-blue-300 uppercase mb-2">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition h-12" required>
                        </div>
                        <div class="md:col-span-2 border-t border-white/10 pt-6 mt-2">
                            <h4 class="text-white font-bold mb-4 flex items-center gap-2 text-sm uppercase tracking-wide">
                                <i class="fa-solid fa-lock text-slate-400"></i> Change Password <span class="text-slate-500 normal-case font-normal">(Optional)</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">New Password</label>
                                    <input type="password" name="password" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition h-12">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition h-12">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: ASSIGN CLIENTS --}}
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 mb-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-briefcase text-emerald-400"></i> Managed Clients
                    </h3>
                    <p class="text-slate-400 text-sm mb-6">Clients checked below are assigned to this manager.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($clients as $client)
                            <label class="relative flex items-center p-4 rounded-xl border cursor-pointer transition-all group
                                {{ in_array($client->id, $assignedClientIds) ? 'bg-emerald-900/20 border-emerald-500/50' : 'bg-white/5 border-white/10 hover:bg-white/10 hover:border-emerald-500/30' }}">
                                
                                <input type="checkbox" name="clients[]" value="{{ $client->id }}" 
                                    class="w-5 h-5 text-emerald-500 bg-slate-800 border-slate-600 rounded focus:ring-emerald-500 focus:ring-offset-slate-900"
                                    {{ in_array($client->id, $assignedClientIds) ? 'checked' : '' }}>
                                
                                <span class="ml-3 text-sm font-bold {{ in_array($client->id, $assignedClientIds) ? 'text-white' : 'text-slate-300 group-hover:text-white' }} transition-colors">
                                    {{ $client->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- SECTION 3: PERMISSIONS --}}
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 mb-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-key text-amber-400"></i> Update Permissions
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($permissions as $permission)
                            <label class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 transition cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $permission }}" 
                                    class="w-5 h-5 text-amber-500 bg-slate-800 border-slate-600 rounded focus:ring-amber-500 focus:ring-offset-slate-900"
                                    {{ in_array($permission, $userPermissions) ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-slate-200 capitalize">{{ str_replace('_', ' ', $permission) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div class="flex justify-end gap-4 border-t border-white/10 pt-8">
                    <a href="{{ route('admin.sub_admins.index') }}" class="px-8 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold transition border border-white/10">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-xl font-bold shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-1">
                        Update Manager
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Scrollbar Polish --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }
    </style>
</x-app-layout>