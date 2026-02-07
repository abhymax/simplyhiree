<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-6xl mx-auto">
            
            {{-- HEADER / BREADCRUMB --}}
            <div class="mb-8 border-b border-white/10 pb-6 flex flex-col md:flex-row justify-between items-end gap-4">
                <div>
                    <a href="{{ route('admin.jobs.pending') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-3 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Back to Pending Queue
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Job Review</h1>
                    <p class="text-blue-200 mt-1 text-lg">Review posting details before going live.</p>
                </div>

                {{-- STATUS BADGE --}}
                <div>
                    @if($job->status === 'approved')
                        <span class="px-5 py-2 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/50 text-sm font-extrabold shadow-lg flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span> LIVE / APPROVED
                        </span>
                    @elseif($job->status === 'pending_approval')
                        <span class="px-5 py-2 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/50 text-sm font-extrabold shadow-lg flex items-center gap-2">
                            <i class="fa-solid fa-clock"></i> PENDING REVIEW
                        </span>
                    @else
                        <span class="px-5 py-2 rounded-full bg-slate-700 text-slate-300 border border-slate-500 text-sm font-extrabold flex items-center gap-2">
                            {{ strtoupper($job->status) }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- LEFT COLUMN: JOB DETAILS --}}
                <div class="lg:col-span-2 space-y-8">
                    
                    {{-- 1. MAIN INFO CARD --}}
                    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-6 opacity-10">
                            <i class="fa-solid fa-briefcase text-8xl text-white"></i>
                        </div>

                        <div class="relative z-10">
                            <h2 class="text-3xl font-bold text-white leading-tight">{{ $job->title }}</h2>
                            <div class="flex items-center gap-2 mt-2 text-amber-300 font-bold text-lg" style="color: #fcd34d !important;">
                                <i class="fa-solid fa-building"></i> {{ $job->company_name }}
                            </div>

                            {{-- META GRID --}}
                            <div class="grid grid-cols-2 gap-4 mt-8">
                                <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                                    <span class="block text-xs font-bold text-cyan-300 uppercase mb-1">Category</span>
                                    <span class="text-white font-medium">{{ $job->category->name ?? 'General' }}</span>
                                </div>
                                <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                                    <span class="block text-xs font-bold text-cyan-300 uppercase mb-1">Type</span>
                                    <span class="text-white font-medium">{{ $job->job_type }}</span>
                                </div>
                                <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                                    <span class="block text-xs font-bold text-cyan-300 uppercase mb-1">Location</span>
                                    <span class="text-white font-medium"><i class="fa-solid fa-location-dot text-rose-400 mr-1"></i> {{ $job->location }}</span>
                                </div>
                                <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                                    <span class="block text-xs font-bold text-cyan-300 uppercase mb-1">Salary</span>
                                    <span class="text-white font-medium">{{ $job->salary ?? 'Not Disclosed' }}</span>
                                </div>
                            </div>

                            {{-- DESCRIPTION --}}
                            <div class="mt-8">
                                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                    <i class="fa-solid fa-align-left text-blue-400"></i> Job Description
                                </h3>
                                <div class="text-blue-100 leading-relaxed text-sm whitespace-pre-wrap bg-slate-900/30 p-6 rounded-2xl border border-white/5">
                                    {{ $job->description }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. REQUIREMENTS CARD --}}
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl p-8 shadow-lg">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-purple-400"></i> Requirements
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase mb-1">Experience Range</span>
                                <div class="text-white font-bold text-lg flex items-center gap-2">
                                    <span class="bg-white/10 px-3 py-1 rounded-lg">{{ $job->min_experience ?? 0 }}</span>
                                    <span>-</span>
                                    <span class="bg-white/10 px-3 py-1 rounded-lg">{{ $job->max_experience ?? 'N/A' }}</span>
                                    <span class="text-sm font-normal text-slate-400">Years</span>
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase mb-1">Education Level</span>
                                <div class="text-white font-bold text-lg">{{ $job->educationLevel->name ?? 'Any' }}</div>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase mb-1">Gender Preference</span>
                                <div class="text-white font-bold text-lg">{{ $job->gender_preference ?? 'Any' }}</div>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase mb-1">Age Range</span>
                                <div class="text-white font-bold text-lg">{{ $job->min_age ?? '18' }} - {{ $job->max_age ?? '60' }} Yrs</div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN: ACTIONS --}}
                <div class="lg:col-span-1 space-y-6">
                    
                    {{-- COMMERCIALS (Gold Card) --}}
                    <div class="bg-gradient-to-br from-amber-500/10 to-orange-500/10 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 shadow-lg">
                        <h3 class="text-amber-300 font-bold text-sm uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-coins"></i> Commercials
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center border-b border-amber-500/20 pb-3">
                                <span class="text-amber-100 text-sm">Payout Amount</span>
                                <span class="text-2xl font-black text-white">₹{{ number_format($job->payout_amount ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-amber-100 text-sm">Maturity Period</span>
                                <span class="text-lg font-bold text-white">{{ $job->minimum_stay_days ?? 0 }} Days</span>
                            </div>
                        </div>
                    </div>

                    {{-- CLIENT INFO --}}
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl p-6">
                        <h3 class="text-slate-400 font-bold text-sm uppercase tracking-wider mb-4">Posted By</h3>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold">
                                {{ substr($job->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="text-white font-bold">{{ $job->user->name }}</div>
                                <div class="text-xs text-blue-300">{{ $job->user->email }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 shadow-2xl sticky top-24">
                        <h3 class="text-white font-bold text-lg mb-4">Admin Actions</h3>
                        
                        @if($job->status === 'pending_approval')
                            <div class="space-y-3">
                                <form action="{{ route('admin.jobs.approve', $job->id) }}" method="POST">
                                    @csrf
                                    {{-- Hidden inputs to preserve existing values if not editing --}}
                                    <input type="hidden" name="payout_amount" value="{{ $job->payout_amount }}">
                                    <input type="hidden" name="minimum_stay_days" value="{{ $job->minimum_stay_days }}">
                                    
                                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-black py-3 rounded-xl font-bold shadow-lg shadow-emerald-500/20 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-check"></i> Approve Job
                                    </button>
                                </form>

                                <form action="{{ route('admin.jobs.reject', $job->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-500 text-white py-3 rounded-xl font-bold shadow-lg shadow-red-600/30 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-xmark"></i> Reject Job
                                    </button>
                                </form>
                            </div>
                        @elseif($job->status === 'approved')
                            <form action="{{ route('admin.jobs.status.update', $job->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="on_hold">
                                <button type="submit" class="w-full bg-orange-500 hover:bg-orange-400 text-black py-3 rounded-xl font-bold shadow-lg transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-pause"></i> Put On Hold
                                </button>
                            </form>
                        @endif

                        <div class="mt-4 pt-4 border-t border-white/10">
                            <form action="{{ route('admin.jobs.destroy', $job->id) }}" method="POST" onsubmit="return confirm('Delete permanently?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-full text-rose-400 hover:text-rose-200 text-sm font-bold transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-trash"></i> Delete Job Permanently
                                </button>
                            </form>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>