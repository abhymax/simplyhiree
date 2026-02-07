<x-app-layout>
    {{-- FULL PAGE BLUE BACKGROUND --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 left-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[120px] opacity-30 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-30"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- BREADCRUMB --}}
            <div class="mb-8">
                <a href="{{ route('admin.applications.index') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-4 transition-colors text-sm font-bold tracking-wide uppercase group">
                    <div class="h-8 w-8 rounded-full bg-white/10 flex items-center justify-center mr-2 group-hover:bg-cyan-500 transition">
                        <i class="fa-solid fa-arrow-left text-white"></i>
                    </div>
                    Back to Applications
                </a>
                <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Application Details</h1>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- LEFT COLUMN: CANDIDATE INFO --}}
                <div class="lg:col-span-2 space-y-8">
                    
                    {{-- 1. Main Profile Card (Glass) --}}
                    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-6 opacity-10">
                            <i class="fa-solid fa-id-card text-8xl text-white"></i>
                        </div>

                        <div class="relative z-10">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                <div class="flex items-center gap-5">
                                    <div class="h-20 w-20 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-3xl shadow-lg ring-4 ring-white/10">
                                        {{ substr($application->candidate->first_name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <h2 class="text-3xl font-bold text-white">{{ $application->candidate->first_name }} {{ $application->candidate->last_name }}</h2>
                                        <div class="flex flex-wrap gap-4 mt-2 text-sm font-medium text-blue-200">
                                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-envelope text-cyan-400"></i> {{ $application->candidate->email }}</span>
                                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-phone text-cyan-400"></i> {{ $application->candidate->phone_number }}</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="bg-blue-600/50 border border-blue-400/50 text-white px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">
                                    ID: #{{ $application->id }}
                                </span>
                            </div>

                            <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-6 border-t border-white/10 pt-6">
                                <div>
                                    <p class="text-xs text-blue-300 font-bold uppercase mb-1">Location</p>
                                    <p class="text-white font-semibold text-lg">{{ $application->candidate->location ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-blue-300 font-bold uppercase mb-1">Gender</p>
                                    <p class="text-white font-semibold text-lg">{{ $application->candidate->gender ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-blue-300 font-bold uppercase mb-1">Experience</p>
                                    <p class="text-white font-semibold text-lg">{{ $application->candidate->experience_status ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-blue-300 font-bold uppercase mb-1">Expected CTC</p>
                                    <p class="text-amber-300 font-bold text-lg">{{ $application->candidate->expected_ctc ?? 'N/A' }}</p>
                                </div>
                            </div>
                            
                            <div class="mt-8">
                                <p class="text-xs text-blue-300 font-bold uppercase mb-3">Key Skills</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(explode(',', $application->candidate->skills) as $skill)
                                        <span class="bg-white/10 text-white px-3 py-1.5 rounded-lg text-sm font-medium border border-white/10 hover:bg-white/20 transition cursor-default">
                                            {{ trim($skill) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Resume Card --}}
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl p-8 shadow-lg">
                        <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-file-lines text-rose-400"></i> Resume / CV
                        </h3>
                        @if($application->candidate->resume_path)
                            <div class="bg-slate-900/50 p-5 rounded-2xl border border-white/10 flex items-center justify-between group hover:border-blue-500/50 transition">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 bg-rose-500/20 rounded-xl flex items-center justify-center text-rose-400">
                                        <i class="fa-solid fa-file-pdf text-2xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-white font-bold text-base group-hover:text-blue-300 transition">Candidate_Resume.pdf</p>
                                        <p class="text-xs text-slate-400">Click download to view</p>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $application->candidate->resume_path) }}" target="_blank" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-0.5">
                                    Download
                                </a>
                            </div>
                        @else
                            <div class="text-center py-8 border-2 border-dashed border-white/10 rounded-2xl">
                                <p class="text-slate-400 italic">No resume uploaded by candidate.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- RIGHT COLUMN: STATUS & ACTIONS --}}
                <div class="lg:col-span-1 space-y-8">
                    
                    {{-- Status Card --}}
                    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl h-full flex flex-col">
                        <h3 class="text-xl font-bold text-white mb-6 border-b border-white/10 pb-4">Current Status</h3>
                        
                        <div class="space-y-6 flex-grow">
                            {{-- Admin Status --}}
                            <div>
                                <p class="text-xs text-blue-300 uppercase font-bold mb-2">Admin Approval</p>
                                @if(strtolower($application->status) == 'approved')
                                    <div class="w-full bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-3 rounded-xl flex items-center gap-3 font-bold">
                                        <div class="h-8 w-8 bg-green-500 text-black rounded-lg flex items-center justify-center"><i class="fa-solid fa-check"></i></div>
                                        Approved
                                    </div>
                                @elseif(strtolower($application->status) == 'rejected')
                                    <div class="w-full bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-xl flex items-center gap-3 font-bold">
                                        <div class="h-8 w-8 bg-red-500 text-white rounded-lg flex items-center justify-center"><i class="fa-solid fa-xmark"></i></div>
                                        Rejected
                                    </div>
                                @else
                                    <div class="w-full bg-amber-500/20 border border-amber-500/50 text-amber-300 px-4 py-3 rounded-xl flex items-center gap-3 font-bold animate-pulse">
                                        <div class="h-8 w-8 bg-amber-500 text-black rounded-lg flex items-center justify-center"><i class="fa-regular fa-clock"></i></div>
                                        Pending Review
                                    </div>
                                @endif
                            </div>

                            {{-- Client Status --}}
                            <div>
                                <p class="text-xs text-blue-300 uppercase font-bold mb-2">Client Progress</p>
                                <div class="w-full bg-slate-900/50 border border-white/10 text-white px-4 py-3 rounded-xl flex items-center justify-between font-medium">
                                    <span>{{ $application->hiring_status ?? 'Pending Client Action' }}</span>
                                    <i class="fa-solid fa-circle-info text-blue-400"></i>
                                </div>
                            </div>

                            {{-- Metadata --}}
                            @if($application->interview_at)
                            <div class="p-4 bg-blue-600/20 rounded-2xl border border-blue-500/30">
                                <p class="text-xs text-blue-300 font-bold uppercase mb-1">Interview Scheduled</p>
                                <p class="text-white font-bold flex items-center gap-2">
                                    <i class="fa-regular fa-calendar text-blue-400"></i> 
                                    {{ \Carbon\Carbon::parse($application->interview_at)->format('M d, Y - h:i A') }}
                                </p>
                            </div>
                            @endif

                            @if($application->joining_date)
                            <div class="p-4 bg-emerald-600/20 rounded-2xl border border-emerald-500/30">
                                <p class="text-xs text-emerald-300 font-bold uppercase mb-1">Joining Date</p>
                                <p class="text-white font-bold flex items-center gap-2">
                                    <i class="fa-solid fa-flag-checkered text-emerald-400"></i>
                                    {{ \Carbon\Carbon::parse($application->joining_date)->format('M d, Y') }}
                                </p>
                            </div>
                            @endif
                        </div>

                        {{-- ACTION BUTTONS (Only if Pending) --}}
                        @if(strtolower($application->status) === 'pending review')
                        <div class="mt-8 pt-6 border-t border-white/10 grid grid-cols-2 gap-4">
                             <form action="{{ route('admin.applications.approve', $application->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-green-500 hover:bg-green-400 text-black py-3.5 rounded-xl font-extrabold shadow-lg shadow-green-500/20 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-check"></i> Approve
                                </button>
                            </form>
                            <form action="{{ route('admin.applications.reject', $application->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-500 text-white py-3.5 rounded-xl font-extrabold shadow-lg shadow-red-600/30 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-xmark"></i> Reject
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>