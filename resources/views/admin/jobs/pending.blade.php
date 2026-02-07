<x-app-layout>
    {{-- FULL PAGE BLUE BACKGROUND WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glow Effects --}}
        <div class="absolute top-0 left-0 w-96 h-96 bg-amber-500 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-cyan-500 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Pending Jobs</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Review and monetize new job postings.</p>
                </div>
                
                @if (session('success'))
                    <div class="mb-4 md:mb-0 px-6 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 rounded-xl font-bold flex items-center shadow-lg backdrop-blur-md animate-bounce-short">
                        <i class="fa-solid fa-circle-check mr-2 text-xl"></i> {{ session('success') }}
                    </div>
                @endif
            </div>

            {{-- MAIN CARD CONTAINER --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                
                {{-- DATA TABLE --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-5">Job Details</th>
                                <th class="px-6 py-5">Requirements</th>
                                <th class="px-6 py-5">Client / Posted By</th>
                                <th class="px-6 py-5 text-right w-[450px]">Actions & Commercials</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse ($jobs as $job)
                                <tr class="group hover:bg-white/5 transition-all duration-200 cursor-default">
                                    
                                    {{-- JOB DETAILS --}}
                                    <td class="px-6 py-6 align-top">
                                        <div class="flex items-start gap-3">
                                            <div class="mt-1 p-2 bg-white/10 rounded-lg text-amber-400">
                                                <i class="fa-solid fa-briefcase text-lg"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-white text-lg leading-tight mb-1">{{ $job->title }}</div>
                                                <div class="text-cyan-200 font-bold text-sm flex items-center gap-1.5">
                                                    <i class="fa-solid fa-building"></i> {{ $job->company_name }}
                                                </div>
                                                <div class="text-slate-400 text-xs mt-2 flex flex-wrap gap-2">
                                                    <span class="bg-white/10 px-2 py-1 rounded border border-white/10 flex items-center gap-1">
                                                        <i class="fa-solid fa-location-dot text-rose-400"></i> {{ $job->location }}
                                                    </span>
                                                    <span class="bg-white/10 px-2 py-1 rounded border border-white/10 flex items-center gap-1">
                                                        <i class="fa-solid fa-users text-blue-400"></i> Openings: <b class="text-white">{{ $job->openings ?? 'N/A' }}</b>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- REQUIREMENTS --}}
                                    <td class="px-6 py-6 align-top">
                                        <div class="space-y-2 text-sm text-blue-100">
                                            <div class="flex items-center gap-2">
                                                <span class="text-slate-400 text-xs w-8 uppercase font-bold">Exp</span> 
                                                <span class="bg-indigo-500/20 text-indigo-200 px-2 py-0.5 rounded text-xs border border-indigo-500/30">
                                                    {{ $job->experienceLevel->name ?? 'Not Specified' }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-slate-400 text-xs w-8 uppercase font-bold">Edu</span>
                                                <span>{{ $job->educationLevel->name ?? 'Not Specified' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-slate-400 text-xs w-8 uppercase font-bold">Age</span>
                                                <span>{{ $job->min_age ?? 'N/A' }} - {{ $job->max_age ?? 'N/A' }} yrs</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-slate-400 text-xs w-8 uppercase font-bold">Gen</span>
                                                <span>{{ $job->gender_preference ?? 'Any' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- CLIENT --}}
                                    <td class="px-6 py-6 align-top">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-500 to-pink-600 flex items-center justify-center text-white font-bold shadow-lg">
                                                {{ substr($job->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-white">{{ $job->user->name }}</div>
                                                <div class="text-xs text-slate-400 mt-0.5">Posted {{ $job->created_at->diffForHumans() }}</div>
                                                <div class="text-[10px] text-slate-500">{{ $job->created_at->format('d M, Y') }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- ACTIONS --}}
                                    <td class="px-6 py-6 align-top text-right">
                                        
                                        {{-- Top Row: Links --}}
                                        <div class="flex justify-end gap-3 mb-4">
                                            <a href="{{ route('admin.jobs.show', $job) }}" class="text-xs font-bold text-cyan-400 hover:text-white bg-cyan-950/50 hover:bg-cyan-600 border border-cyan-500/30 px-3 py-1.5 rounded-lg transition-all">
                                                <i class="fa-regular fa-eye mr-1"></i> View Details
                                            </a>
                                            <a href="{{ route('admin.jobs.manage', $job) }}" class="text-xs font-bold text-indigo-400 hover:text-white bg-indigo-950/50 hover:bg-indigo-600 border border-indigo-500/30 px-3 py-1.5 rounded-lg transition-all">
                                                <i class="fa-solid fa-sliders mr-1"></i> Manage Partners
                                            </a>
                                        </div>

                                        {{-- Approval Form (Dark Inputs) --}}
                                        <form action="{{ route('admin.jobs.approve', $job) }}" method="POST">
                                            @csrf
                                            <div class="bg-slate-800/50 rounded-xl p-3 border border-white/10 flex flex-col gap-3 shadow-inner">
                                                
                                                <div class="flex gap-2">
                                                    <div class="flex-1">
                                                        <label class="sr-only">Payout</label>
                                                        <div class="relative">
                                                            <span class="absolute left-3 top-2 text-slate-400 text-xs">₹</span>
                                                            <input type="number" name="payout_amount" placeholder="Payout" required
                                                                class="w-full pl-6 pr-2 py-1.5 bg-slate-900 border border-slate-600 rounded-lg text-white text-sm placeholder-slate-500 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                                        </div>
                                                    </div>
                                                    <div class="flex-1">
                                                        <label class="sr-only">Stay Days</label>
                                                        <div class="relative">
                                                            <span class="absolute right-3 top-2 text-slate-400 text-[10px] uppercase">Days</span>
                                                            <input type="number" name="minimum_stay_days" placeholder="Stay" required
                                                                class="w-full pl-3 pr-8 py-1.5 bg-slate-900 border border-slate-600 rounded-lg text-white text-sm placeholder-slate-500 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex gap-2">
                                                    <button type="submit" class="flex-1 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-400 hover:to-green-500 text-white py-2 rounded-lg font-bold text-xs shadow-lg shadow-emerald-900/50 transition transform hover:scale-[1.02] flex items-center justify-center gap-1">
                                                        <i class="fa-solid fa-check"></i> Approve
                                                    </button>
                                                    
                                                    {{-- Reject Button (Separate Form Trigger or handled here?) --}}
                                                    {{-- Note: Cannot nest forms. Moving Reject button OUTSIDE or using JS. --}}
                                                    {{-- Better approach for Blade: Use `formaction` attribute on buttons if in same form, but routes differ. --}}
                                                    {{-- Safest approach: Keep separate forms but positioned absolutely or via layout. --}}
                                                </div>
                                            </div>
                                        </form>

                                        <form action="{{ route('admin.jobs.reject', $job) }}" method="POST" class="mt-2 text-right">
                                            @csrf
                                            <button type="submit" class="text-rose-400 hover:text-rose-200 text-xs font-bold underline decoration-rose-500/30 hover:decoration-rose-500 transition">
                                                Reject Posting
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-20 text-center">
                                        <div class="bg-white/5 inline-block p-6 rounded-full mb-4 backdrop-blur-md border border-white/10 shadow-xl">
                                            <i class="fa-solid fa-clipboard-check text-5xl text-emerald-400"></i>
                                        </div>
                                        <p class="text-2xl font-bold text-white">All Caught Up!</p>
                                        <p class="text-blue-200 mt-2">There are no jobs pending approval right now.</p>
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