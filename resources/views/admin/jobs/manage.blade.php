<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glow Effects --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-rose-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-5xl mx-auto">
            
            {{-- HEADER --}}
            <div class="mb-8 border-b border-white/10 pb-6">
                <a href="{{ route('admin.jobs.pending') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-4 transition-colors text-sm font-bold tracking-wide uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Pending Jobs
                </a>
                <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Partner Visibility</h1>
                <p class="text-blue-200 mt-1 text-lg font-medium">Control who can access and work on this vacancy.</p>
            </div>

            {{-- JOB CONTEXT CARD --}}
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-6 mb-8 shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-6 opacity-10">
                    <i class="fa-solid fa-shield-halved text-8xl text-white"></i>
                </div>
                
                <div class="relative z-10">
                    <h2 class="text-2xl font-bold text-white mb-1">{{ $job->title }}</h2>
                    <div class="flex items-center gap-2 text-amber-300 font-bold mb-6 text-sm">
                        <i class="fa-solid fa-building"></i> {{ $job->company_name }}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                            <span class="block text-xs font-bold text-blue-300 uppercase mb-1">Location</span>
                            <span class="text-white font-medium"><i class="fa-solid fa-location-dot text-rose-400 mr-1"></i> {{ $job->location }}</span>
                        </div>
                        <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                            <span class="block text-xs font-bold text-blue-300 uppercase mb-1">Experience</span>
                            <span class="text-white font-medium">{{ $job->experienceLevel->name ?? 'Not Specified' }}</span>
                        </div>
                        <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                            <span class="block text-xs font-bold text-blue-300 uppercase mb-1">Education</span>
                            <span class="text-white font-medium">{{ $job->educationLevel->name ?? 'Not Specified' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- EXCLUSION FORM --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden p-8">
                
                <form action="{{ route('admin.jobs.exclusions.update', $job->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-white mb-2 flex items-center gap-2">
                            <i class="fa-solid fa-ban text-rose-500"></i> Exclude Partners
                        </h3>
                        <p class="text-sm text-blue-200 mb-6 bg-blue-500/10 p-3 rounded-lg border border-blue-500/20 inline-block">
                            <i class="fa-solid fa-circle-info mr-1"></i> Selected partners will <strong>NOT</strong> see this job in their dashboard.
                        </p>

                        @if($allPartners->isEmpty())
                            <div class="bg-amber-500/20 border border-amber-500/50 p-6 rounded-2xl text-center">
                                <p class="text-amber-300 font-bold text-lg">No partners found in the system.</p>
                                <p class="text-amber-200 text-sm mt-1">Onboard partners first to manage visibility.</p>
                            </div>
                        @else
                            {{-- Partner Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                                @foreach($allPartners as $partner)
                                    <label class="relative flex items-start p-4 rounded-xl border cursor-pointer transition-all duration-200 group hover:border-blue-400
                                        {{ in_array($partner->id, $excludedPartnerIds) ? 'bg-rose-900/20 border-rose-500/50' : 'bg-slate-800/50 border-white/10' }}">
                                        
                                        <div class="flex items-center h-5">
                                            <input id="partner_{{ $partner->id }}" 
                                                   name="excluded_partners[]" 
                                                   type="checkbox" 
                                                   value="{{ $partner->id }}"
                                                   class="w-5 h-5 text-rose-600 bg-slate-900 border-slate-600 rounded focus:ring-rose-500 focus:ring-2"
                                                   {{ in_array($partner->id, $excludedPartnerIds) ? 'checked' : '' }}>
                                        </div>
                                        
                                        <div class="ml-3 text-sm">
                                            <span class="block font-bold text-white group-hover:text-blue-300 transition-colors">
                                                {{ $partner->name }}
                                            </span>
                                            <span class="block text-slate-400 text-xs mt-0.5">{{ $partner->email }}</span>
                                            
                                            {{-- Status Indicator --}}
                                            @if(in_array($partner->id, $excludedPartnerIds))
                                                <span class="inline-block mt-2 text-[10px] uppercase font-bold text-rose-400 tracking-wider">
                                                    <i class="fa-solid fa-lock mr-1"></i> Blocked
                                                </span>
                                            @else
                                                <span class="inline-block mt-2 text-[10px] uppercase font-bold text-emerald-400 tracking-wider">
                                                    <i class="fa-solid fa-check mr-1"></i> Allowed
                                                </span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end pt-6 border-t border-white/10">
                        <a href="{{ route('admin.jobs.pending') }}" class="mr-4 px-6 py-3 rounded-xl text-sm font-bold text-white hover:bg-white/10 transition border border-transparent hover:border-white/10">
                            Cancel
                        </a>
                        <button type="submit" class="bg-rose-600 hover:bg-rose-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-rose-600/30 transition transform hover:-translate-y-1 flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Save Changes
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Custom Scrollbar Style for this page --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }
    </style>
</x-app-layout>