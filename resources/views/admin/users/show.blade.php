<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>

        <div class="relative z-10 max-w-5xl mx-auto">
            
            <div class="mb-8 border-b border-white/10 pb-6">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-emerald-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Candidates
                </a>
                <h1 class="text-4xl font-extrabold text-white">{{ $user->name }}</h1>
                <p class="text-blue-200 mt-1 text-lg font-medium">Candidate Profile Details</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Profile Card --}}
                <div class="md:col-span-1">
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 shadow-2xl text-center">
                        <div class="h-32 w-32 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-4xl font-bold border-4 border-slate-700 shadow-xl mx-auto mb-4">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                        <p class="text-slate-400 text-sm mt-1">{{ $user->email }}</p>
                        
                        <div class="mt-6 pt-6 border-t border-white/10 text-left space-y-3">
                            <div><span class="text-xs font-bold text-slate-500 uppercase block">Mobile</span> <span class="text-white">{{ optional($user->candidate)->mobile ?? 'N/A' }}</span></div>
                            <div><span class="text-xs font-bold text-slate-500 uppercase block">Joined</span> <span class="text-white">{{ $user->created_at->format('M d, Y') }}</span></div>
                            <div><span class="text-xs font-bold text-slate-500 uppercase block">Status</span> 
                                <span class="text-emerald-400 font-bold uppercase text-xs">{{ $user->status }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Details Card --}}
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-lg">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2"><i class="fa-solid fa-address-card text-emerald-400"></i> Personal Details</h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div><span class="text-xs font-bold text-slate-500 uppercase block mb-1">First Name</span><span class="text-white bg-white/5 px-3 py-2 rounded block">{{ optional($user->candidate)->first_name ?? '-' }}</span></div>
                            <div><span class="text-xs font-bold text-slate-500 uppercase block mb-1">Last Name</span><span class="text-white bg-white/5 px-3 py-2 rounded block">{{ optional($user->candidate)->last_name ?? '-' }}</span></div>
                            <div><span class="text-xs font-bold text-slate-500 uppercase block mb-1">Date of Birth</span><span class="text-white bg-white/5 px-3 py-2 rounded block">{{ optional($user->candidate)->dob ?? '-' }}</span></div>
                            <div><span class="text-xs font-bold text-slate-500 uppercase block mb-1">Gender</span><span class="text-white bg-white/5 px-3 py-2 rounded block">{{ optional($user->candidate)->gender ?? '-' }}</span></div>
                        </div>
                    </div>

                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-lg">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2"><i class="fa-solid fa-briefcase text-blue-400"></i> Professional Info</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div><span class="text-xs font-bold text-slate-500 uppercase block mb-1">Skills</span><span class="text-white bg-white/5 px-3 py-2 rounded block">{{ optional($user->candidate)->skills ?? 'No skills listed' }}</span></div>
                            <div><span class="text-xs font-bold text-slate-500 uppercase block mb-1">Resume</span>
                                @if(optional($user->candidate)->resume_path)
                                    <a href="{{ asset('storage/' . $user->candidate->resume_path) }}" target="_blank" class="text-emerald-400 hover:text-white underline">Download Resume</a>
                                @else
                                    <span class="text-slate-500 italic">Not uploaded</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>